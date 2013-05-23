<?php
namespace Aura\Sql;

use PDO;

abstract class AbstractConnectionTest extends \PHPUnit_Framework_TestCase
{
    protected $extension;
    
    protected $connection;
    
    protected $schema1 = 'aura_test_schema1';
    
    protected $schema2 = 'aura_test_schema2';
    
    protected $table = 'aura_test_table';
    
    protected $expect_dsn_string;
    
    protected $expect_quote_scalar;
    
    protected $expect_quote_array;
    
    protected $expect_quote_values_in;
    
    protected $expect_quote_values_in_many;
    
    protected $expect_append_limit = "LIMIT 10";
    
    protected $expect_append_limit_offset = "LIMIT 10 OFFSET 20";
    
    public function setUp()
    {
        parent::setUp();
        
        // skip if we don't have the extension
        if (! extension_loaded($this->extension)) {
            $this->markTestSkipped("Extension '{$this->extension}' not loaded.");
        }
        
        // what is the test class?
        $test_class = get_class($this);
        
        // transform the test class name to a setup class name
        $setup_class = str_replace(
            ['Aura\Sql\\',       'ConnectionTest'],
            ['Aura\Sql\Setup\\', 'Setup'],
            $test_class
        );
        
        // do the setup
        $this->setup = new $setup_class;
        $this->connection = $this->setup->exec(
            $this->table,
            $this->schema1,
            $this->schema2
        );
        
        // set properties from configs
        $this->expect_dsn_string = $GLOBALS[$test_class]['expect_dsn_string'];
    }
    
    public function testGetDsnString()
    {
        $actual = $this->connection->getDsnString();
        $this->assertEquals($this->expect_dsn_string, $actual);
    }
    
    public function testSetPdo()
    {
        $pdo = new PDO('sqlite::memory:');
        $this->connection->setPdo($pdo);
        $actual = $this->connection->getPdo();
        $this->assertSame($pdo, $actual);
    }
    
    public function testGetPdo()
    {
        $actual = $this->connection->getPdo();
        $this->assertInstanceOf('\PDO', $actual);
    }
    
    public function testQuery()
    {
        $text = "SELECT * FROM {$this->table}";
        $stmt = $this->connection->query($text);
        $this->assertInstanceOf('PDOStatement', $stmt);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $expect = 10;
        $actual = count($result);
        $this->assertEquals($expect, $actual);
    }
    
    public function testQueryWithData()
    {
        $text = "SELECT * FROM {$this->table} WHERE id <= :val";
        $bind['val'] = '5';
        $stmt = $this->connection->query($text, $bind);
        $this->assertInstanceOf('PDOStatement', $stmt);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $expect = 5;
        $actual = count($result);
        $this->assertEquals($expect, $actual);
    }
    
    public function testQueryWithArrayData()
    {
        $text = "SELECT * FROM {$this->table} WHERE id IN (:list) OR id = :id";
        
        $bind['list'] = [1, 2, 3, 4];
        $bind['id'] = 5;
        
        $stmt = $this->connection->query($text, $bind);
        $this->assertInstanceOf('PDOStatement', $stmt);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $expect = 5;
        $actual = count($result);
        $this->assertEquals($expect, $actual);
    }
    
    public function testPrepareWithQuotedStringsAndData()
    {
        $text = "SELECT * FROM {$this->table}
                 WHERE 'leave :foo alone'
                 AND id IN (:list)
                 AND \"leave :bar alone\"";
        
        $bind = [
            'list' => [1, 2, 3, 4, 5],
            'foo' => 'WRONG',
            'bar' => 'WRONG',
        ];
        
        $stmt = $this->connection->prepare($text, $bind);
        
        $expect = str_replace(':list', '1, 2, 3, 4, 5', $text);
        $actual = $stmt->queryString;
        $this->assertSame($expect, $actual);
    }
    
    public function testFetchAll()
    {
        $text = "SELECT * FROM {$this->table}";
        $result = $this->connection->fetchAll($text);
        $expect = 10;
        $actual = count($result);
        $this->assertEquals($expect, $actual);
    }
    
    public function testFetchAssoc()
    {
        $text = "SELECT * FROM {$this->table} ORDER BY id";
        $result = $this->connection->fetchAssoc($text);
        $expect = 10;
        $actual = count($result);
        $this->assertEquals($expect, $actual);
        
        // 1-based IDs, not 0-based sequential values
        $expect = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $actual = array_keys($result);
        $this->assertEquals($expect, $actual);
    }
    
    public function testFetchColumn()
    {
        $text = "SELECT id FROM {$this->table} ORDER BY id";
        $result = $this->connection->fetchColumn($text);
        $expect = 10;
        $actual = count($result);
        $this->assertEquals($expect, $actual);
        
        // // 1-based IDs, not 0-based sequential values
        $expect = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
        $this->assertEquals($expect, $result);
    }
    
    public function testFetchValue()
    {
        $text = "SELECT id FROM {$this->table} WHERE id = 1";
        $actual = $this->connection->fetchValue($text);
        $expect = '1';
        $this->assertEquals($expect, $actual);
    }
    
    public function testFetchPairs()
    {
        $text = "SELECT id, name FROM {$this->table} ORDER BY id";
        $actual = $this->connection->fetchPairs($text);
        $expect = [
          1  => 'Anna',
          2  => 'Betty',
          3  => 'Clara',
          4  => 'Donna',
          5  => 'Fiona',
          6  => 'Gertrude',
          7  => 'Hanna',
          8  => 'Ione',
          9  => 'Julia',
          10 => 'Kara',
        ];
        $this->assertEquals($expect, $actual);
    }
    
    public function testFetchOne()
    {
        $text = "SELECT id, name FROM {$this->table} WHERE id = 1";
        $actual = $this->connection->fetchOne($text);
        $expect = [
            'id'   => '1',
            'name' => 'Anna',
        ];
        $this->assertEquals($expect, $actual);
    }
    
    public function testQuoteValue()
    {
        // quote a scalar
        $actual = $this->connection->quoteValue('"foo" bar \'baz\'');
        $this->assertEquals($this->expect_quote_scalar, $actual);
        
        // quote a number
        $actual = $this->connection->quoteValue(123.456);
        $this->assertEquals(123.456, $actual);
        
        // quote a numeric
        $actual = $this->connection->quoteValue('123.456');
        $this->assertEquals(123.456, $actual);
        
        // quote an array
        $actual = $this->connection->quoteValue(array('"foo"', 'bar', "'baz'"));
        $this->assertEquals($this->expect_quote_array, $actual);
    }
    
    public function testQuoteValuesIn()
    {
        // no placeholders
        $actual = $this->connection->quoteValuesIn('foo = bar', "'zim'");
        $expect = 'foo = bar';
        $this->assertEquals($expect, $actual);
        
        // one placeholder, one value
        $actual = $this->connection->quoteValuesIn("foo = ?", "'bar'");
        $this->assertEquals($this->expect_quote_values_in,$actual);
        
        // many placeholders, many values
        $actual = $this->connection->quoteValuesIn("foo = ? AND zim = ?", ["'bar'", "'baz'"]);
        $this->assertEquals($this->expect_quote_values_in_many, $actual);
        
        // many placeholders, too many values
        $actual = $this->connection->quoteValuesIn("foo = ? AND zim = ?", ["'bar'", "'baz'", "'gir'"]);
        $this->assertEquals($this->expect_quote_values_in_many, $actual);
    }
    
    public function testQuoteName()
    {
        // table AS alias
        $actual = $this->connection->quoteName('table AS alias');
        $this->assertEquals($this->expect_quote_name_table_as_alias, $actual);
        
        // table.col AS alias
        $actual = $this->connection->quoteName('table.col AS alias');
        $this->assertEquals($this->expect_quote_name_table_col_as_alias, $actual);
        
        // table alias
        $actual = $this->connection->quoteName('table alias');
        $this->assertEquals($this->expect_quote_name_table_alias, $actual);
        
        // table.col alias
        $actual = $this->connection->quoteName('table.col alias');
        $this->assertEquals($this->expect_quote_name_table_col_alias, $actual);
        
        // plain old identifier
        $actual = $this->connection->quoteName('table');
        $this->assertEquals($this->expect_quote_name_plain, $actual);
        
        // star
        $actual = $this->connection->quoteName('*');
        $this->assertEquals('*', $actual);
        
        // star dot star
        $actual = $this->connection->quoteName('*.*');
        $this->assertEquals('*.*', $actual);
    }
    
    public function testQuoteNamesIn()
    {
        $sql = "*, *.*, foo.bar, CONCAT('foo.bar', \"baz.dib\") AS zim";
        $actual = $this->connection->quoteNamesIn($sql);
        $this->assertEquals($this->expect_quote_names_in, $actual);
    }
    
    public function testLastInsertId()
    {
        $cols = ['name' => 'Laura'];
        $this->insert($this->table, $cols);
        $expect = 11;
        $actual = $this->fetchLastInsertId();
        $this->assertEquals($expect, $actual);
    }
    
    public function testTransactions()
    {
        // data
        $cols = ['name' => 'Laura'];

        // begin and rollback
        $this->connection->beginTransaction();
        $this->insert($this->table, $cols);
        $actual = $this->connection->fetchAll("SELECT * FROM {$this->table}");
        $this->assertSame(11, count($actual));
        $this->connection->rollback();
        $actual = $this->connection->fetchAll("SELECT * FROM {$this->table}");
        $this->assertSame(10, count($actual));
        
        // begin and commit
        $this->connection->beginTransaction();
        $this->insert($this->table, $cols);
        $actual = $this->connection->fetchAll("SELECT * FROM {$this->table}");
        $this->connection->commit();
        $this->assertSame(11, count($actual));
    }
    
    public function testAppendLimit()
    {
        $text = '';
        $this->connection->appendLimit($text, 10);
        $this->assertSame($this->expect_append_limit, trim($text));
        
        $text = '';
        $this->connection->appendLimit($text, 10, 20);
        $this->assertSame($this->expect_append_limit_offset, trim($text));
    }
    
    protected function insert($table, array $data)
    {
        $cols = array_keys($data);
        $vals = [];
        foreach ($cols as $col) {
            $vals[] = ":$col";
        }
        $cols = implode(', ', $cols);
        $vals = implode(', ', $vals);
        $text = "INSERT INTO {$table} ({$cols}) VALUES ({$vals})";
        $this->connection->query($text, $data);
    }
    
    protected function fetchLastInsertId()
    {
        return $this->connection->lastInsertId($this->table, 'id');
    }
}
