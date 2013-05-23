<?php
namespace Aura\Sql;

class MockSignal implements SignalInterface
{
    /**
     * 
     * The signal handlers to be executed.
     * 
     * @var array
     * 
     */
    protected $handlers = [];

    /**
     * 
     * Adds a handler to the list.
     * 
     * @param object|string $origin The object or class name sending the signal.
     * 
     * @param string $signal The signal being sent.
     * 
     * @param callable $callback The callback to execute when the signal
     * is sent.
     * 
     * @return void
     * 
     */
    public function handler($origin, $signal, $callback)
    {
        $this->handlers[$signal] = $callback;
    }

    /**
     * 
     * Sends a signal to the handlers.
     * 
     * @param object $origin The object sending the signal.
     * 
     * @param string $signal The signal being sent.
     * 
     * @return void
     * 
     */
    public function send($origin, $signal)
    {
        // do nothing
    }
    
    public function getHandlers()
    {
        return $this->handlers;
    }
}
