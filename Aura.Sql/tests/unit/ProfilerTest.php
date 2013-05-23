<?php
namespace Aura\Sql;

use PDO;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    protected $profiler;
    
    protected $pdo;
    
    protected function setUp()
    {
        $this->pdo = new Pdo('sqlite::memory:');
        $this->pdo->query('CREATE TABLE test (id INTEGER, name VARCHAR(16))');
        $this->signal = new MockSignal;
        $this->connection = new MockConnection(null, null, null, [], $this->signal);
        $this->profiler = new Profiler;
    }
    
    public function testRegisterSignalHandlers()
    {
        $this->profiler->registerSignalHandlers($this->signal);
        
        $actual = $this->signal->getHandlers();
        $expect = [
            ConnectionInterface::SIGNAL_PRE_CONNECT            => [$this->profiler, 'beginCall'],
            ConnectionInterface::SIGNAL_POST_CONNECT           => [$this->profiler, 'endCall'], 
            ConnectionInterface::SIGNAL_PRE_QUERY              => [$this->profiler, 'beginQuery'], 
            ConnectionInterface::SIGNAL_POST_QUERY             => [$this->profiler, 'endQuery'], 
            ConnectionInterface::SIGNAL_PRE_BEGIN_TRANSACTION  => [$this->profiler, 'beginCall'], 
            ConnectionInterface::SIGNAL_POST_BEGIN_TRANSACTION => [$this->profiler, 'endCall'], 
            ConnectionInterface::SIGNAL_PRE_COMMIT             => [$this->profiler, 'beginCall'], 
            ConnectionInterface::SIGNAL_POST_COMMIT            => [$this->profiler, 'endCall'], 
            ConnectionInterface::SIGNAL_PRE_ROLL_BACK          => [$this->profiler, 'beginCall'], 
            ConnectionInterface::SIGNAL_POST_ROLL_BACK         => [$this->profiler, 'endCall'], 
        ];
        
        foreach ($expect as $name => $info) {
            $this->assertEquals($expect[$name], $actual[$name]);
        }
    }
    
    public function testSetAndIsActive()
    {
        $this->assertFalse($this->profiler->isActive());
        $this->profiler->setActive(true);
        $this->assertTrue($this->profiler->isActive());
        $this->profiler->setActive(false);
        $this->assertFalse($this->profiler->isActive());
    }
    
    public function testQuery()
    {
        $text = 'SELECT * FROM test';
        $stmt = $this->pdo->prepare($text);
        $bind = ['foo' => 'bar'];
        
        // not active, should be nothing in the profile
        $this->profiler->beginQuery($this->connection, $stmt, $bind);
        $this->profiler->endQuery($this->connection, $stmt, $bind);
        $expect = [];
        $actual = $this->profiler->getEntries();
        $this->assertSame($expect, $actual);
        
        // now make it active
        $this->profiler->setActive(true);
        $this->profiler->beginQuery($this->connection, $stmt, $bind);
        $this->profiler->endQuery($this->connection,   $stmt, $bind);
        $actual = $this->profiler->getEntries();
        $this->assertSame(1, count($actual));
        $this->assertSame($text, $actual[0]->text);
        $this->assertSame($bind, $actual[0]->data);
    }
    
    public function testCall()
    {
        $text = '__CALL__';
        
        // not active, should be nothing in the profile
        $this->profiler->beginCall($this->connection, $text);
        $this->profiler->endCall($this->connection, $text);
        $expect = [];
        $actual = $this->profiler->getEntries();
        $this->assertSame($expect, $actual);
        
        // now make it active
        $this->profiler->setActive(true);
        $this->profiler->beginCall($this->connection, $text);
        $this->profiler->endCall($this->connection, $text);
        $actual = $this->profiler->getEntries();
        $this->assertSame(1, count($actual));
        $this->assertSame($text, $actual[0]->text);
    }
}
