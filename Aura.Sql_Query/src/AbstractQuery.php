<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Sql_Query
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Sql_Query;

use Aura\Sql\ConnectionInterface;

/**
 * 
 * Abstract query object for Select, Insert, Update, and Delete.
 * 
 * @package Aura.Sql_Query
 * 
 */
abstract class AbstractQuery
{
    /**
     * 
     * An SQL connection.
     * 
     * @var ConnectionInterface
     * 
     */
    protected $connection;

    /**
     * 
     * Data to bind the query.
     * 
     * @var array
     * 
     */
    protected $bind = [];

    /**
     * 
     * Constructor.
     * 
     * @param ConnectionInterface $connection An SQL connection.
     * 
     * @return void
     * 
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * 
     * Converts this query object to a string.
     * 
     * @return string
     * 
     */
    abstract public function __toString();

    /**
     * 
     * Gets the database connection for this query object.
     * 
     * @return AbstractConnection
     * 
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * 
     * Returns an array as an indented comma-separated values string.
     * 
     * @param array $list The values to convert.
     * 
     * @return string
     * 
     */
    protected function indentCsv(array $list)
    {
        return PHP_EOL
             . '    ' . implode(',' . PHP_EOL . '    ', $list)
             . PHP_EOL;
    }

    /**
     * 
     * Returns an array as an indented string.
     * 
     * @param array $list The values to convert.
     * 
     * @return string
     * 
     */
    protected function indent($list)
    {
        return PHP_EOL
             . '    ' . implode(PHP_EOL . '    ', $list)
             . PHP_EOL;
    }

    /**
     * 
     * Adds values to bind into the query; merges with existing values.
     * 
     * @param array $bind Values to bind to the query.
     * 
     * @return void
     * 
     */
    public function bind(array $bind)
    {
        $this->bind = array_merge($this->bind, $bind);
    }

    /**
     * 
     * Sets values to bind into the query; this overrides any previous
     * bindings.
     * 
     * @param array $bind Values to bind to the query.
     * 
     * @return void
     * 
     */
    public function clearBind()
    {
        $this->bind = [];
    }

    /**
     * 
     * Gets the values to bind into the query.
     * 
     * @return array
     * 
     */
    public function getBind()
    {
        return $this->bind;
    }
    
    /**
     * 
     * Execute the query and return a PDO statement.
     * 
     * @return PDOStatement
     * 
     * @see Aura\Sql\Connection::query()
     * 
     */
    public function exec()
    {
        return $this->connection->query($this->__toString(), $this->getBind());
    }
}
