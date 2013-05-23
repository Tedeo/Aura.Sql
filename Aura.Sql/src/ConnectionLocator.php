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

/**
 * 
 * Manages connections to default, read, and write databases.
 * 
 * @package Aura.Sql
 * 
 */
class ConnectionLocator
{
    /**
     * 
     * A registry of connection objects.
     * 
     * @var array
     * 
     */
    protected $registry = [
        'default' => null,
        'read' => [],
        'write' => [],
    ];

    /**
     * 
     * Whether or not registry entries have been converted to objects.
     * 
     * @var array
     * 
     */
    protected $converted = [
        'default' => false,
        'read' => [],
        'write' => [],
    ];
    
    /**
     * 
     * Constructor.
     * 
     * @param callable $default A callable to create a default connection.
     * 
     * @param array $read An array of callables to create read connections.
     * 
     * @param array $write An array of callables to create write connections.
     * 
     */
    public function __construct(
        $default,
        array $read = [],
        array $write = []
    ) {
        $this->setDefault($default);
        foreach ($read as $name => $spec) {
            $this->setRead($name, $spec);
        }
        foreach ($write as $name => $spec) {
            $this->setWrite($name, $spec);
        }
    }

    /**
     * 
     * Sets the default connection registry entry.
     * 
     * @param callable $spec The registry entry.
     * 
     * @return void
     * 
     */
    public function setDefault($spec)
    {
        $this->registry['default'] = $spec;
        $this->converted['default'] = false;
    }

    /**
     * 
     * Returns the default connection object.
     * 
     * @return ConnectionInterface
     * 
     */
    public function getDefault()
    {
        if (! $this->converted['default']) {
            $func = $this->registry['default'];
            $this->registry['default'] = $func();
            $this->converted['default'] = true;
        }
        
        return $this->registry['default'];
    }

    /**
     * 
     * Sets a read connection registry entry by name.
     * 
     * @param string $name The name of the registry entry.
     * 
     * @param callable $spec The registry entry.
     * 
     * @return void
     * 
     */
    public function setRead($name, $spec)
    {
        $this->registry['read'][$name] = $spec;
        $this->converted['read'][$name] = false;
    }

    /**
     * 
     * Returns a read connection by name; if no name is given, picks a
     * random connection; if no read connections are present, returns the
     * default connection.
     * 
     * @param string $name The read connection name to return.
     * 
     * @return ConnectionInterface
     * 
     */
    public function getRead($name = null)
    {
        return $this->getConnection('read', $name);
    }

    /**
     * 
     * Sets a write connection registry entry by name.
     * 
     * @param string $name The name of the registry entry.
     * 
     * @param callable $spec The registry entry.
     * 
     * @return void
     * 
     */
    public function setWrite($name, $spec)
    {
        $this->registry['write'][$name] = $spec;
        $this->converted['write'][$name] = false;
    }

    /**
     * 
     * Returns a write connection by name; if no name is given, picks a
     * random connection; if no write connections are present, returns the
     * default connection.
     * 
     * @param string $name The write connection name to return.
     * 
     * @return ConnectionInterface
     * 
     */
    public function getWrite($name = null)
    {
        return $this->getConnection('write', $name);
    }
    
    /**
     * 
     * Returns a connection by name.
     * 
     * @param string $type The connection type ('read' or 'write').
     * 
     * @param string $name The name of the connection.
     * 
     * @return ConnectionInterface
     * 
     */
    protected function getConnection($type, $name)
    {
        if (! $this->registry[$type]) {
            return $this->getDefault();
        }
        
        if (! $name) {
            $name = array_rand($this->registry[$type]);
        }
        
        if (! isset($this->registry[$type][$name])) {
            throw new Exception\ConnectionNotFound("{$type}:{$name}");
        }
        
        if (! $this->converted[$type][$name]) {
            $func = $this->registry[$type][$name];
            $this->registry[$type][$name] = $func();
            $this->converted[$type][$name] = true;
        }
        
        return $this->registry[$type][$name];
    }
}
