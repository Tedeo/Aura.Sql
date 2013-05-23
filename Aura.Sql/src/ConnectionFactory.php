<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Sql
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Sql;

use Aura\Sql\Query\Factory as QueryFactory;

/**
 * 
 * A factory for connection objects.
 * 
 * @package Aura.Sql
 * 
 */
class ConnectionFactory
{
    /**
     * 
     * A map of short adapter names to fully-qualified classes.
     * 
     * @var array
     * 
     */
    protected $map = [
        'mysql'  => 'Aura\Sql\MysqlConnection',
        'pgsql'  => 'Aura\Sql\PgsqlConnection',
        'sqlite' => 'Aura\Sql\SqliteConnection',
        'sqlsrv' => 'Aura\Sql\SqlsrvConnection',
    ];

    protected $signal;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $map An override map of connection names to classes.
     * 
     */
    public function __construct(
        SignalInterface $signal,
        array $map = []
    ) {
        $this->signal = $signal;
        $this->map = array_merge($this->map, $map);
    }

    /**
     * 
     * Returns a new connection instance.
     * 
     * @param string $adapter The short adapter name.
     * 
     * @param mixed $dsn The DSN for the connection.
     * 
     * @param string $username The username for the connection.
     * 
     * @param string $password The password for the connection.
     * 
     * @param array $options PDO options for the connection.
     * 
     * @return AbstractConnection
     * 
     */
    public function newInstance(
        $adapter,
        $dsn = null,
        $username = null,
        $password = null,
        $options = []
    ) {
        $class = $this->map[$adapter];
        return new $class(
            $dsn,
            $username,
            $password,
            $options,
            $this->signal
        );
    }
}
