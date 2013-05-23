<?php
namespace Aura\Sql_Query;

use Aura\Sql\MockConnection;
use Aura\Sql\MockSignal;

abstract class AbstractQueryTest extends \PHPUnit_Framework_TestCase
{
    protected $query_type;
    
    protected $query;

    protected $query_factory;
    
    protected $connection;
    
    protected function setUp()
    {
        parent::setUp();
        $this->connection = new MockConnection(null, null, null, [], new MockSignal);
        $this->query_factory = new QueryFactory($this->connection);
        $this->query = $this->newQuery();
    }
    
    protected function tearDown()
    {
        parent::tearDown();
    }

    protected function newQuery()
    {
        $method = 'new' . $this->query_type;
        return $this->query_factory->$method();
    }
    
    public function testGetConnection()
    {
        $connection = $this->query->getConnection();
        $this->assertSame($this->connection, $connection);
    }
    
    public function testSetAddGetBind()
    {
        $actual = $this->query->getBind();
        $this->assertSame([], $actual);
        
        $expect = ['foo' => 'bar', 'baz' => 'dib'];
        $this->query->bind($expect);
        $actual = $this->query->getBind();
        $this->assertSame($expect, $actual);
        
        $this->query->bind(['zim' => 'gir']);
        $expect = ['foo' => 'bar', 'baz' => 'dib', 'zim' => 'gir'];
        $actual = $this->query->getBind();
        $this->assertSame($expect, $actual);
    }
    
    protected function assertSameSql($expect, $actual)
    {
        $expect = trim($expect);
        $expect = preg_replace('/^\s*/m', '', $expect);
        $expect = preg_replace('/\s*$/m', '', $expect);
        
        $actual = trim($actual);
        $actual = preg_replace('/^\s*/m', '', $actual);
        $actual = preg_replace('/\s*$/m', '', $actual);
        
        $this->assertSame($expect, $actual);
    }
}
