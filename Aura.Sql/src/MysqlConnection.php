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

/**
 * 
 * MySQL connection adapter.
 * 
 * @package Aura.Sql
 * 
 */
class MysqlConnection extends AbstractConnection
{
    /**
     * 
     * The PDO DSN for the connection. This can be an array of key-value pairs
     * or a string (minus the PDO type prefix).
     * 
     * @var string|array
     * 
     */
    protected $dsn = [
        'host' => null,
        'port' => null,
        'dbname' => null,
        'unix_socket' => null,
        'charset' => null,
    ];

    /**
     * 
     * The PDO type prefix.
     * 
     * @var string
     * 
     */
    protected $dsn_prefix = 'mysql';

    /**
     * 
     * The prefix to use when quoting identifier names.
     * 
     * @var string
     * 
     */
    protected $quote_name_prefix = '`';

    /**
     * 
     * The suffix to use when quoting identifier names.
     * 
     * @var string
     * 
     */
    protected $quote_name_suffix = '`';

    /**
     * 
     * Returns the last ID inserted on the connection.
     * 
     * @return mixed
     * 
     */
    public function lastInsertId()
    {
        return $this->getPdo()->lastInsertId();
    }
}
