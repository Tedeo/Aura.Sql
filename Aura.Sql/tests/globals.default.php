<?php
/**
 * Aura\Sql\Setup\MysqlSetup
 */
$GLOBALS['Aura\Sql\Setup\MysqlSetup']['connection_params'] = [
    'dsn' => [
        'host' => '127.0.0.1',
    ],
    'username' => 'root',
    'password' => '',
    'options' => [],
];

/**
 * Aura\Sql\MysqlConnectionTest
 */
$GLOBALS['Aura\Sql\MysqlConnectionTest']['expect_dsn_string'] = 'mysql:host=127.0.0.1';

/**
 * Aura\Sql\Setup\PgsqlSetup
 */
$GLOBALS['Aura\Sql\Setup\PgsqlSetup']['connection_params'] = [
    'dsn' => [
        'host' => '127.0.0.1',
        'dbname' => 'test',
    ],
    'username' => 'postgres',
    'password' => '',
    'options' => [],
];

/**
 * Aura\Sql\PgsqlConnectionTest
 */
$GLOBALS['Aura\Sql\PgsqlConnectionTest']['expect_dsn_string'] = 'pgsql:host=127.0.0.1;dbname=test';

/**
 * Aura\Sql\Setup\SqliteSetup
 */
$GLOBALS['Aura\Sql\Setup\SqliteSetup']['connection_params'] = [
    'dsn' => ':memory:',
];

/**
 * Aura\Sql\SqliteConnectionTest
 */
$GLOBALS['Aura\Sql\SqliteConnectionTest']['expect_dsn_string'] = 'sqlite::memory:';
