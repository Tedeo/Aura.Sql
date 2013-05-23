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
 * Creates query statement objects.
 * 
 * @package Aura.Sql_Query
 * 
 */
class QueryFactory
{
    protected $connection;
    
    public function __construct(ConnectionInterface $connection = null)
    {
        $this->connection = $connection;
    }
    
    public function newSelect(ConnectionInterface $connection = null)
    {
        return $this->newInstance('Select', $connection);
    }
    
    public function newInsert(ConnectionInterface $connection = null)
    {
        return $this->newInstance('Insert', $connection);
    }
    
    public function newUpdate(ConnectionInterface $connection = null)
    {
        return $this->newInstance('Update', $connection);
    }
    
    public function newDelete(ConnectionInterface $connection = null)
    {
        return $this->newInstance('Delete', $connection);
    }
    
    protected function newInstance($type, ConnectionInterface $connection = null)
    {
        $class = '\Aura\Sql_Query\\' . $type;
        
        if (! $connection) {
            $connection = $this->connection;
        }
        
        if (! $connection) {
            throw new \Exception("No connection for query.");
        }
        
        return new $class($connection);
    }    
}
