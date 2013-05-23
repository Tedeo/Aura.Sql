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

/**
 * 
 * An object for INSERT queries.
 * 
 * @package Aura.Sql_Query
 * 
 */
class Insert extends AbstractQuery
{
    use ValuesTrait;

    /**
     * 
     * The table to insert into.
     * 
     * @var string
     * 
     */
    protected $table;

    /**
     * 
     * Returns this object as an SQL statement string.
     * 
     * @return string An SQL statement string.
     * 
     */
    public function __toString()
    {
        return 'INSERT INTO ' . $this->table . ' ('
             . $this->indentCsv(array_keys($this->values))
             . ') VALUES ('
             . $this->indentCsv(array_values($this->values))
             . ')';
    }

    /**
     * 
     * Sets the table to insert into.
     * 
     * @param string $table The table to insert into.
     * 
     * @return $this
     * 
     */
    public function into($table)
    {
        $this->table = $this->connection->quoteName($table);
        return $this;
    }
    
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
}
