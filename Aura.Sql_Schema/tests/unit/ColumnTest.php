<?php
namespace Aura\Sql_Schema;

class ColumnTest extends \PHPUnit_Framework_TestCase
{
    protected $info = [
        'name' => 'cost',
        'type' => 'numeric',
        'size' => 10,
        'scale' => 2,
        'notnull' => true,
        'default' => null,
        'autoinc' => false,
        'primary' => false,
    ];

    public function setup()
    {
        $this->col = new Column(
            $this->info['name'],
            $this->info['type'],
            $this->info['size'],
            $this->info['scale'],
            $this->info['notnull'],
            $this->info['default'],
            $this->info['autoinc'],
            $this->info['primary']
        );
    }
    
    public function testConstruct()
    {
        foreach ($this->info as $key => $expect) {
            $this->assertSame($expect, $this->col->$key);
        }
    }
    
    public function test__set_state()
    {
        eval('$actual = ' . var_export($this->col, true) . ';');
        foreach ($this->info as $key => $expect) {
            $this->assertSame($expect, $actual->$key);
        }
    }
    
    public function test__isset()
    {
        $this->assertTrue(isset($this->col->name));
        $this->assertFalse(isset($this->col->no_such_property));
    }
}
