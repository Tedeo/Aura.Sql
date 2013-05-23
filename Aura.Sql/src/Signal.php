<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @package Aura.Sql
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Sql;

/**
 * 
 * A basic signal manager for executing controller hooks.
 * 
 * @package Aura.Sql
 * 
 */
class Signal implements SignalInterface
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
        if (isset($this->handlers[$signal])) {
            $args = func_get_args();
            array_pop($args);
            array_pop($args);
            $func = $this->handlers[$signal];
            call_user_func_array($func, $args);
        }
    }
}
