<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Sql_Schema
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Sql_Schema;

/**
 * 
 * MySQL connection adapter.
 * 
 * @package Aura.Sql_Schema
 * 
 */
class MysqlSchema extends AbstractSchema
{
    /**
     * 
     * Returns an list of tables in the database.
     * 
     * @param string $schema Optionally, pass a schema name to get the list
     * of tables in this schema.
     * 
     * @return array The list of tables in the database.
     * 
     */
    public function fetchTables($schema = null)
    {
        $text = 'SHOW TABLES';
        if ($schema) {
            $text .= ' IN ' . $this->connection->quoteName($schema);
        }
        return $this->connection->fetchColumn($text);
    }

    /**
     * 
     * Returns an array of columns in a table.
     * 
     * @param string $spec Return the columns in this table. This may be just
     * a `table` name, or a `schema.table` name.
     * 
     * @return array An associative array where the key is the column name
     * and the value is a Column object.
     * 
     */
    public function fetchColumns($spec)
    {
        list($schema, $table) = $this->splitName($spec);

        $table = $this->connection->quoteName($table);
        $text = "SHOW COLUMNS FROM $table";

        if ($schema) {
            $schema = preg_replace('/[^\w]/', '', $schema);
            $schema = $this->connection->quoteName($schema);
            $text .= " IN $schema";
        }

        // get the column descriptions
        $raw_cols = $this->connection->fetchAll($text);

        // where the column info will be stored
        $cols = [];

        // loop through the result rows; each describes a column.
        foreach ($raw_cols as $val) {

            $name = $val['Field'];
            list($type, $size, $scale) = $this->getTypeSizeScope($val['Type']);

            // save the column description
            $cols[$name] = $this->column_factory->newInstance(
                $name,
                $type,
                ($size  ? (int) $size  : null),
                ($scale ? (int) $scale : null),
                (bool) ($val['Null'] != 'YES'),
                $this->getDefault($val['Default']),
                (bool) (strpos($val['Extra'], 'auto_increment') !== false),
                (bool) ($val['Key'] == 'PRI')
            );
        }

        // done!
        return $cols;
    }

    /**
     * 
     * A helper method to get the default value for a column.
     * 
     * @param string $default The default value as reported by MySQL.
     * 
     * @return string
     * 
     */
    protected function getDefault($default)
    {
        $upper = strtoupper($default);
        if ($upper == 'NULL' || $upper == 'CURRENT_TIMESTAMP') {
            // the only non-literal allowed by MySQL is "CURRENT_TIMESTAMP"
            return null;
        } else {
            // return the literal default
            return $default;
        }
    }
}
