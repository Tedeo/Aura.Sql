<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Sql_Mapper
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Sql_Mapper;

use Aura\Sql\ConnectionLocator;
use Aura\Sql_Query\QueryFactory;
use Aura\Sql_Query\Select;
use Aura\Sql_Query\Insert;
use Aura\Sql_Query\Update;
use Aura\Sql_Query\Delete;

/**
 * 
 * A TableDataGateway implementation.
 * 
 * @package Aura.Sql_Mapper
 * 
 */
class Gateway
{
    /**
     * 
     * A ConnectionLocator for database connections.
     * 
     * @var ConnectionLocator
     * 
     */
    protected $connections;

    /**
     * 
     * A mapper between this table gateway and entities.
     * 
     * @var AbstractMapper
     * 
     */
    protected $mapper;

    protected $query_factory;
    
    /**
     * 
     * Constructor.
     * 
     * @param ConnectionLocator $connections A ConnectionLocator for database
     * connections.
     * 
     * @param AbstractMapper $mapper A table-to-entity mapper.
     * 
     */
    public function __construct(
        ConnectionLocator $connections,
        QueryFactory $query_factory,
        AbstractMapper $mapper
    ) {
        $this->connections = $connections;
        $this->query_factory = $query_factory;
        $this->mapper   = $mapper;
    }

    /**
     * 
     * Gets the connection locator.
     * 
     * @return ConnectionLocator
     * 
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * 
     * Gets the mapper.
     * 
     * @return ConnectionLocator
     * 
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * 
     * Inserts an entity into the mapped table using a write connection.
     * 
     * @param object $entity The entity to insert.
     * 
     * @return int The last insert ID.
     * 
     */
    public function insert($entity)
    {
        $connection = $this->connections->getWrite();
        $insert = $this->query_factory->newInsert($connection);
        $this->mapper->modifyInsert($insert, $entity);
        $insert->exec();
        return $insert->lastInsertId();
    }

    /**
     * 
     * Updates an entity in the mapped table using a write connection; if an
     * array of initial data is present, updates only changed values.
     * 
     * @param object $entity The entity to update.
     * 
     * @param array $initial_data Initial data for the entity.
     * 
     * @return bool True if the update succeeded, false if not.  (This is
     * determined by checking the number of rows affected by the query.)
     * 
     */
    public function update($entity, $initial_data = null)
    {
        $connection = $this->connections->getWrite();
        $update = $this->query_factory->newUpdate($connection);
        $this->mapper->modifyUpdate($update, $entity, $initial_data);
        $stmt = $update->exec();
        return (bool) $stmt->rowCount();
    }

    /**
     * 
     * Deletes an entity from the mapped table using a write connection.
     * 
     * @param object $entity The entity to delete.
     * 
     * @return bool True if the delete succeeded, false if not.  (This is
     * determined by checking the number of rows affected by the query.)
     * 
     */
    public function delete($entity)
    {
        $connection = $this->connections->getWrite();
        $delete = $this->query_factory->newDelete($connection);
        $this->mapper->modifyDelete($delete, $entity);
        $stmt = $delete->exec();
        return (bool) $stmt->rowCount();
    }

    /**
     * 
     * Returns a new Select object for the mapped table using a read
     * connection.
     * 
     * @param array $cols Select these columns from the table; when empty,
     * selects all mapped columns.
     * 
     * @return Select
     * 
     */
    public function select(array $cols = [])
    {
        $connection = $this->connections->getRead();
        $select = $this->query_factory->newSelect($connection);
        $this->mapper->modifySelect($select, $cols);
        return $select;
    }

    /**
     * 
     * Selects one row from the mapped table for a given column and value(s).
     * 
     * @param string $col The column to use for matching.
     * 
     * @param mixed $val The value(s) to match against; this can be an array
     * of values.
     * 
     * @return array
     * 
     */
    public function fetchOneBy($col, $val)
    {
        return $this->selectBy($col, $val)->fetchOne();
    }

    /**
     * 
     * Selects all rows from the mapped table for a given column and value.
     * 
     * @param string $col The column to use for matching.
     * 
     * @param mixed $val The value(s) to match against; this can be an array
     * of values.
     * 
     * @return array
     * 
     */
    public function fetchAllBy($col, $val)
    {
        return $this->selectBy($col, $val)->fetchAll();
    }

    /**
     * 
     * Creates a Select object to match against a given column and value(s).
     * 
     * @param string $col The column to use for matching.
     * 
     * @param mixed $val The value(s) to match against; this can be an array
     * of values.
     * 
     * @return Select
     * 
     */
    protected function selectBy($col, $val)
    {
        $select = $this->select();
        $where = $this->getMapper()->getTableCol($col);
        if (is_array($val)) {
            $where .= ' IN (?)';
        } else {
            $where .= ' = ?';
        }
        $select->where($where, $val);
        return $select;
    }
}
