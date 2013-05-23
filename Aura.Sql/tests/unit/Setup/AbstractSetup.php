<?php
namespace Aura\Sql\Setup;

use Aura\Sql\ConnectionFactory;
use Aura\Sql\Signal;

abstract class AbstractSetup
{
    protected $adapter;
    
    protected $connection;
    
    protected $connection_params = [
        'dsn'      => [],
        'username' => null,
        'password' => null,
        'options'  => [],
    ];
    
    protected $table;
    
    protected $schema1;
    
    protected $schema2;
    
    public function exec($table, $schema1, $schema2)
    {
        $this->table = $table;
        $this->schema1 = $schema1;
        $this->schema2 = $schema2;
        
        $this->connection = $this->newConnection();
        
        $this->dropSchemas();
        $this->createSchemas();
        $this->createTables();
        $this->fillTable();
        
        return $this->connection;
    }
    
    public function newConnection()
    {
        $setup_class = get_class($this);
        
        $connection_params = array_merge(
            $this->connection_params,
            $GLOBALS[$setup_class]['connection_params']
        );
        
        $signal = new Signal;
        $connection_factory = new ConnectionFactory(new Signal);
        
        return $connection_factory->newInstance(
            $this->adapter,
            $connection_params['dsn'],
            $connection_params['username'],
            $connection_params['password'],
            $connection_params['options']
        );
    }
    
    abstract protected function createSchemas();
    
    abstract protected function dropSchemas();
    
    protected function createTables()
    {
        // create in schema 1
        $sql = $this->create_table;
        $this->connection->query($sql);
        
        // create again in schema 2
        $sql = str_replace($this->table, "{$this->schema2}.{$this->table}", $sql);
        $this->connection->query($sql);
    }
    
    // only fills in schema 1
    protected function fillTable()
    {
        $names = [
            'Anna', 'Betty', 'Clara', 'Donna', 'Fiona',
            'Gertrude', 'Hanna', 'Ione', 'Julia', 'Kara',
        ];
        
        foreach ($names as $name) {
            $this->insert($this->table, ['name' => $name]);
        }
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
    
}
