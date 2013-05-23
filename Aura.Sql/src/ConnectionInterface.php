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

use PDO;
use PDOStatement;

/**
 * 
 * Abstract class for SQL connections.
 * 
 * @package Aura.Sql
 * 
 */
interface ConnectionInterface
{
    const SIGNAL_PRE_CONNECT            = 'pre_connect';
    const SIGNAL_POST_CONNECT           = 'post_connect';
    const SIGNAL_PRE_QUERY              = 'pre_query';
    const SIGNAL_POST_QUERY             = 'post_query';
    const SIGNAL_PRE_BEGIN_TRANSACTION  = 'pre_begin_transaction';
    const SIGNAL_POST_BEGIN_TRANSACTION = 'post_begin_transaction';
    const SIGNAL_PRE_COMMIT             = 'pre_commit';
    const SIGNAL_POST_COMMIT            = 'post_commit';
    const SIGNAL_PRE_ROLL_BACK          = 'pre_roll_back';
    const SIGNAL_POST_ROLL_BACK         = 'post_roll_back';
    
    /**
     * 
     * Returns the DSN string used by the PDO connection.
     * 
     * @return string
     * 
     */
    public function getDsnString();

    /**
     * 
     * Sets the PDO connection object; typically used when a shared PDO object
     * already exists in a legacy context.
     * 
     * Note that if you use setPdo(), the pre- and post-connect method hooks
     * will not be called.
     * 
     * @param PDO $pdo The PDO object.
     * 
     * @return void
     * 
     */
    public function setPdo(PDO $pdo);

    /**
     * 
     * Returns the PDO connection object; if it does not exist, creates it to
     * connect to the database.
     * 
     * @return PDO
     * 
     */
    public function getPdo();

    /**
     * 
     * Connects to the database by creating the PDO object.
     * 
     * @return void
     * 
     */
    public function connect();

    /**
     * 
     * Prepares and executes an SQL query, optionally binding values
     * to named placeholders in the query text.
     * 
     * @param string|AbstractQuery $query The text of the SQL query; or, a
     * query object.
     * 
     * @param array $bind An associative array of data to bind to named
     * placeholders in the query.
     * 
     * @return PDOStatement
     * 
     */
    public function query($query, array $bind = []);

    /**
     * 
     * Begins a database transaction and turns off autocommit.
     * 
     * @return mixed
     * 
     */
    public function beginTransaction();

    /**
     * 
     * Commits the current database transaction and turns autocommit back on.
     * 
     * @return mixed
     * 
     */
    public function commit();

    /**
     * 
     * Rolls back the current database transaction and turns autocommit back
     * on.
     * 
     * @return mixed
     * 
     */
    public function rollback();

    /**
     * 
     * Creates a prepared PDOStatement and binds data values to placeholders.
     * 
     * PDO itself is touchy about binding values.  If you attempt to bind a
     * value that does not have a corresponding placeholder, PDO will error.
     * This method checks the query text to find placeholders and binds only
     * data values that have placeholders in the text.
     * 
     * Similarly, PDO won't bind an array value. This method checks to see if
     * the data to be bound is an array; if it is, the array is quoted and
     * replaced into the text directly instead of binding it.
     * 
     * @param string $text The text of the SQL query.
     * 
     * @param array $bind The values to bind (or quote) into the PDOStatement.
     * 
     * @return PDOStatement
     * 
     */
    public function prepare($text, array $bind);

    /**
     * 
     * Fetches a sequential array of all rows from the database; the rows
     * are represented as associative arrays.
     * 
     * @param string|AbstractQuery $query The text of the SQL query; or, a
     * query object.
     * 
     * @param array $bind An associative array of data to bind to the named
     * placeholders.
     * 
     * @return array
     * 
     */
    public function fetchAll($query, $bind = []);

    /**
     * 
     * Fetches an associative array of all rows from the database; the rows
     * are represented as associative arrays. The array of all rows is keyed
     * on the first column of each row.
     * 
     * N.b.: if multiple rows have the same first column value, the last
     * row with that value will override earlier rows.
     * 
     * @param string $query The text of the SQL statement, optionally with
     * named placeholders.
     * 
     * @param array $data An associative array of data to bind to the named
     * placeholders.
     * 
     * @return array
     * 
     */
    public function fetchAssoc($query, array $data = []);

    /**
     * 
     * Fetches the first column of all rows as a sequential array.
     * 
     * @param string|AbstractQuery $query The text of the SQL query; or, a
     * query object.
     * 
     * @param array $bind An associative array of data to bind to the named
     * placeholders.
     * 
     * @return array
     * 
     */
    public function fetchColumn($query, array $bind = []);

    /**
     * 
     * Fetches the very first value (i.e., first column of the first row).
     * 
     * @param string|AbstractQuery $query The text of the SQL query; or, a
     * query object.
     * 
     * @param array $bind An associative array of data to bind to the named
     * placeholders.
     * 
     * @return mixed
     * 
     */
    public function fetchValue($query, array $bind = []);

    /**
     * 
     * Fetches an associative array of all rows as key-value pairs (first 
     * column is the key, second column is the value).
     * 
     * @param string|AbstractQuery $query The text of the SQL query; or, a
     * query object.
     * 
     * @param array $bind An associative array of data to bind to the named
     * placeholders.
     * 
     * @return array
     * 
     */
    public function fetchPairs($query, array $bind = []);

    /**
     * 
     * Fetches one row from the database as an associative array.
     * 
     * @param string|AbstractQuery $query The text of the SQL query; or, a
     * query object.
     * 
     * @param array $bind An associative array of data to bind to the named
     * placeholders.
     * 
     * @return array
     * 
     */
    public function fetchOne($query, array $bind = []);

    /**
     * 
     * Modifies an SQL string **in place** to append a `LIMIT ... OFFSET` clause.
     * 
     * @param string $text The SQL string.
     * 
     * @param int $count The number of rows to return.
     * 
     * @param int $offset Skip this many rows first.
     * 
     * @return void
     * 
     */
    public function appendLimit(&$text, $count, $offset = 0);

    /**
     * 
     * Safely quotes a value for an SQL statement.
     * 
     * If an array is passed as the value, the array values are quoted
     * and then returned as a comma-separated string; this is useful 
     * for generating `IN()` lists.
     * 
     * @param mixed $val The value to quote.
     * 
     * @return string An SQL-safe quoted value (or a string of 
     * separated-and-quoted values).
     * 
     */
    public function quoteValue($val);

    /**
     * 
     * Quotes a value and places into a piece of text at a placeholder; the
     * placeholder is a question-mark.
     * 
     * @param string $text The text with placeholder(s).
     * 
     * @param mixed $bind The data value(s) to quote.
     * 
     * @return mixed An SQL-safe quoted value (or string of separated values)
     * placed into the original text.
     * 
     * @see quote()
     * 
     */
    public function quoteValuesIn($text, $bind);
    
    /**
     * 
     * Quotes a single identifier name (table, table alias, table column, 
     * index, sequence).
     * 
     * If the name contains `' AS '`, this method will separately quote the
     * parts before and after the `' AS '`.
     * 
     * If the name contains a space, this method will separately quote the
     * parts before and after the space.
     * 
     * If the name contains a dot, this method will separately quote the
     * parts before and after the dot.
     * 
     * @param string $spec The identifier name to quote.
     * 
     * @return string|array The quoted identifier name.
     * 
     * @see replaceName()
     * 
     */
    public function quoteName($spec);

    /**
     * 
     * Quotes all fully-qualified identifier names ("table.col") in a string,
     * typically an SQL snippet for a SELECT clause.
     * 
     * Does not quote identifier names that are string literals (i.e., inside
     * single or double quotes).
     * 
     * Looks for a trailing ' AS alias' and quotes the alias as well.
     * 
     * @param string $text The string in which to quote fully-qualified
     * identifier names to quote.
     * 
     * @return string|array The string with names quoted in it.
     * 
     * @see replaceNamesIn()
     * 
     */
    public function quoteNamesIn($text);
}
