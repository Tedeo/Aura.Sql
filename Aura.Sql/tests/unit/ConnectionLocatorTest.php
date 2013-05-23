<?php
namespace Aura\Sql;

class ConnectionLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionLocator
     */
    protected $locator;
    
    protected $default;
    
    protected $read = [];
    
    protected $write = [];
    
    protected function setUp()
    {
        $this->default = function () {
            return new MockConnection(
                ['host' => 'default.example.com'],
                'user_name',
                'pass_word',
                [],
                new Signal
            );
        };
        
        $this->read = [
            'read1' => function () {
                return new MockConnection(
                    ['host' => 'read1.example.com'],
                    'user_name',
                    'pass_word',
                    [],
                    new Signal
                );
            },
            'read2' => function () {
                return new MockConnection(
                    ['host' => 'read2.example.com'],
                    'user_name',
                    'pass_word',
                    [],
                    new Signal
                );
            },
            'read3' => function () {
                return new MockConnection(
                    ['host' => 'read3.example.com'],
                    'user_name',
                    'pass_word',
                    [],
                    new Signal
                );
            },
        ];
        
        $this->write = [
            'write1' => function () {
                return new MockConnection(
                    ['host' => 'write1.example.com'],
                    'user_name',
                    'pass_word',
                    [],
                    new Signal
                );
            },
            'write2' => function () {
                return new MockConnection(
                    ['host' => 'write2.example.com'],
                    'user_name',
                    'pass_word',
                    [],
                    new Signal
                );
            },
            'write3' => function () {
                return new MockConnection(
                    ['host' => 'write3.example.com'],
                    'user_name',
                    'pass_word',
                    [],
                    new Signal
                );
            },
        ];
    }
    
    protected function newLocator($read = [], $write = [])
    {
        return new ConnectionLocator($this->default, $read, $write);
    }
    
    public function testGetDefault()
    {
        $locator = $this->newLocator();
        $conn = $locator->getDefault();
        $expect = 'mock:host=default.example.com';
        $actual = $conn->getDsnString();
        $this->assertSame($expect, $actual);
    }
    
    public function testGetReadDefault()
    {
        $locator = $this->newLocator();
        $conn = $locator->getRead();
        $expect = 'mock:host=default.example.com';
        $actual = $conn->getDsnString();
        $this->assertSame($expect, $actual);
    }
    
    public function testGetReadRandom()
    {
        $locator = $this->newLocator($this->read, $this->write);
        
        $expect = [
            'mock:host=read1.example.com',
            'mock:host=read2.example.com',
            'mock:host=read3.example.com',
        ];
        
        // try 10 times to make sure we get lots of random responses
        for ($i = 1; $i <= 10; $i++) {
            $conn = $locator->getRead();
            $actual = $conn->getDsnString();
            $this->assertTrue(in_array($actual, $expect));
        }
    }
    
    public function testGetReadName()
    {
        $locator = $this->newLocator($this->read, $this->write);
        $conn = $locator->getRead('read2');
        $expect = 'mock:host=read2.example.com';
        $actual = $conn->getDsnString();
        $this->assertSame($expect, $actual);
    }
    
    public function testGetReadMissing()
    {
        $locator = $this->newLocator($this->read, $this->write);
        $this->setExpectedException('Aura\Sql\Exception\ConnectionNotFound');
        $conn = $locator->getRead('no-such-connection');
    }
    
    public function testGetWriteDefault()
    {
        $locator = $this->newLocator();
        $conn = $locator->getWrite();
        $expect = 'mock:host=default.example.com';
        $actual = $conn->getDsnString();
        $this->assertSame($expect, $actual);
    }
    
    public function testGetWriteRandom()
    {
        $locator = $this->newLocator($this->write, $this->write);
        
        $expect = [
            'mock:host=write1.example.com',
            'mock:host=write2.example.com',
            'mock:host=write3.example.com',
        ];
        
        // try 10 times to make sure we get lots of random responses
        for ($i = 1; $i <= 10; $i++) {
            $conn = $locator->getWrite();
            $actual = $conn->getDsnString();
            $this->assertTrue(in_array($actual, $expect));
        }
    }
    
    public function testGetWriteName()
    {
        $locator = $this->newLocator($this->write, $this->write);
        $conn = $locator->getWrite('write2');
        $expect = 'mock:host=write2.example.com';
        $actual = $conn->getDsnString();
        $this->assertSame($expect, $actual);
    }
    
    public function testGetWriteMissing()
    {
        $locator = $this->newLocator($this->write, $this->write);
        $this->setExpectedException('Aura\Sql\Exception\ConnectionNotFound');
        $conn = $locator->getWrite('no-such-connection');
    }
}
