<?php
namespace Aura\Sql_Schema;

use PDO;

abstract class AbstractSchemaTest extends \PHPUnit_Framework_TestCase
{
    protected $extension;
    
    protected $adapter;
    
    protected $connection;
    
    protected $schema;
    
    protected $schema1 = 'aura_test_schema1';
    
    protected $schema2 = 'aura_test_schema2';
    
    protected $table = 'aura_test_table';
    
    protected $expect_fetch_tables;
    
    protected $expect_fetch_columns;
    
    public function setUp()
    {
        parent::setUp();
        
        // skip if we don't have the extension
        if (! extension_loaded($this->extension)) {
            $this->markTestSkipped("Extension '{$this->extension}' not loaded.");
        }
        
        // the test class
        $test_class = get_class($this);
        
        // transform the test class name to a setup class name
        $setup_class = str_replace(
            ['Aura\Sql_Schema\\', 'SchemaTest'],
            ['Aura\Sql\Setup\\',  'Setup'],
            $test_class
        );
        
        // do the setup
        $this->setup = new $setup_class;
        $this->connection = $this->setup->exec(
            $this->table,
            $this->schema1,
            $this->schema2
        );
        
        // the schema class and object
        $schema_class = substr($test_class, 0, -4);
        $this->schema = new $schema_class($this->connection, new ColumnFactory);
        
        // convert column arrays to objects
        foreach ($this->expect_fetch_columns as $name => $info) {
            $this->expect_fetch_columns[$name] = new Column(
                $info['name'],
                $info['type'],
                $info['size'],
                $info['scale'],
                $info['notnull'],
                $info['default'],
                $info['autoinc'],
                $info['primary']
            );
        }
    }
    
    public function testFetchTables()
    {
        $actual = $this->schema->fetchTables();
        $this->assertEquals($this->expect_fetch_tables, $actual);
    }
    
    public function testFetchTables_schema()
    {
        $actual = $this->schema->fetchTables('aura_test_schema2');
        $this->assertEquals($this->expect_fetch_tables_schema, $actual);
    }
    
    public function testFetchColumns()
    {
        $actual = $this->schema->fetchColumns($this->table);
        $expect = $this->expect_fetch_columns;
        ksort($actual);
        ksort($expect);
        $this->assertSame(count($expect), count($actual));
        foreach (array_keys($expect) as $name) {
            $this->assertEquals($expect[$name], $actual[$name]);
        }
    }
    
    public function testFetchColumns_schema()
    {
        $actual = $this->schema->fetchColumns("aura_test_schema2.{$this->table}");
        $expect = $this->expect_fetch_columns;
        ksort($actual);
        ksort($expect);
        $this->assertSame(count($expect), count($actual));
        foreach ($expect as $name => $info) {
            $this->assertEquals($expect[$name], $actual[$name]);
        }
    }
}
