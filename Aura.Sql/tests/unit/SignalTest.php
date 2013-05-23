<?php
namespace Aura\Sql;

class SignalTest extends \PHPUnit_Framework_TestCase
{
    public $value_set_from_handler;
    
    protected $signal;
    
    protected function setUp()
    {
        $this->signal = new Signal;
    }
    
    protected function tearDown()
    {
        parent::tearDown();
    }
    
    public function testSendWithKnownHandler()
    {
        // create a handler
        $self = $this;
        $handler = function () use ($self) {
            $self->value_set_from_handler = 'foo';
        };
        
        // register a handler for a signal
        $this->signal->handler($this, 'set_value', $handler);
        
        // send the signal
        $this->signal->send($this, 'set_value');
        
        // did it work?
        $this->assertSame('foo', $this->value_set_from_handler);
    }
    
    public function testSendWithUnknownHandler()
    {
        // create a handler
        $self = $this;
        $handler = function () use ($self) {
            $self->value_set_from_handler = 'foo';
        };
        
        // send the signal, but no handler has been set for it
        $this->signal->send($this, 'set_value');
        
        // did it work?
        $this->assertNull($this->value_set_from_handler);
    }
}
