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

use PDOStatement;

/**
 * 
 * Retains query profiles.
 * 
 * @package Aura.Sql
 * 
 */
class Profiler
{
    /**
     * 
     * Is the profiler active?
     * 
     * @var bool
     * 
     */
    protected $active = false;

    /**
     * 
     * The microtime before the profiled activity begins.
     * 
     * @var bool
     * 
     */
    protected $before = null;
    
    /**
     * 
     * Retained profile entries.
     * 
     * @var array
     * 
     */
    protected $entries = [];

    /**
     * 
     * Registers this profiler with a signal manager.
     * 
     * @param SignalInterface $signal The signal manager.
     * 
     * @return void
     * 
     */
    public function registerSignalHandlers(SignalInterface $signal)
    {
        $signals = [
            ConnectionInterface::SIGNAL_PRE_CONNECT            => 'beginCall',
            ConnectionInterface::SIGNAL_POST_CONNECT           => 'endCall', 
            ConnectionInterface::SIGNAL_PRE_QUERY              => 'beginQuery', 
            ConnectionInterface::SIGNAL_POST_QUERY             => 'endQuery', 
            ConnectionInterface::SIGNAL_PRE_BEGIN_TRANSACTION  => 'beginCall', 
            ConnectionInterface::SIGNAL_POST_BEGIN_TRANSACTION => 'endCall', 
            ConnectionInterface::SIGNAL_PRE_COMMIT             => 'beginCall', 
            ConnectionInterface::SIGNAL_POST_COMMIT            => 'endCall', 
            ConnectionInterface::SIGNAL_PRE_ROLL_BACK          => 'beginCall', 
            ConnectionInterface::SIGNAL_POST_ROLL_BACK         => 'endCall', 
        ];
        
        foreach ($signals as $signal_name => $method) {
            $signal->handler(
                'Aura\Sql\ConnectionInterface',
                $signal_name,
                [$this, $method]
            );
        }
    }
    
    /**
     * 
     * Turns the profiler on and off.
     * 
     * @param bool $active True to turn on, false to turn off.
     * 
     * @return void
     * 
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;
    }

    /**
     * 
     * Is the profiler active?
     * 
     * @return bool
     * 
     */
    public function isActive()
    {
        return (bool) $this->active;
    }

    /**
     * 
     * Begins a profile timer for a generic call.
     * 
     * @return void
     * 
     */
    public function beginCall(ConnectionInterface $conn, $call)
    {
        if (! $this->isActive()) {
            return;
        }
        
        $this->before = microtime(true);
    }

    /**
     * 
     * Ends a profile timer for a generic call.
     * 
     * @return void
     * 
     */
    public function endCall(ConnectionInterface $conn, $call)
    {
        if (! $this->isActive()) {
            return;
        }
        
        $after = microtime(true);
        $this->addEntry($call, $after - $this->before);
        $this->before = null;
    }

    /**
     * 
     * Begins a profile timer for a query.
     * 
     * @param PDOStatement $stmt The PDOStatement to execute and profile.
     * 
     * @param array $bind The data that was bound into the statement.
     * 
     * @return mixed
     * 
     */
    public function beginQuery(
        ConnectionInterface $conn,
        PDOStatement $stmt,
        array $bind = []
    ) {
        if (! $this->isActive()) {
            return;
        }
        
        $this->before = microtime(true);
    }

    /**
     * 
     * Ends a profile timer for a query.
     * 
     * @param PDOStatement $stmt The PDOStatement to execute and profile.
     * 
     * @param array $bind The data that was bound into the statement.
     * 
     * @return mixed
     * 
     */
    public function endQuery(
        ConnectionInterface $conn,
        PDOStatement $stmt,
        array $bind = []
    ) {
        if (! $this->isActive()) {
            return;
        }
        
        $after = microtime(true);
        $this->addEntry(
            $stmt->queryString,
            $after - $this->before,
            $bind
        );
        
        $this->before = null;
    }

    /**
     * 
     * Adds a profile to the profiler.
     * 
     * @param string $text The text (typically an SQL query) being profiled.
     * 
     * @param float $time The elapsed time in seconds.
     * 
     * @param array $data The data that was used.
     * 
     * @param string $trace An exception backtrace as a string.
     * 
     * @return mixed
     * 
     */
    public function addEntry($text, $time, array $data = null)
    {
        $e = new Exception;
        $this->entries[] = (object) [
            'text' => $text,
            'time' => $time,
            'data' => $data,
            'trace' => $e->getTraceAsString()
        ];
    }

    /**
     * 
     * Returns all the profile entries.
     * 
     * @return array
     * 
     */
    public function getEntries()
    {
        return $this->entries;
    }
}
