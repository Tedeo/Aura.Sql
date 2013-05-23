<?php
namespace Aura\Sql;

class MockConnection extends AbstractConnection
{
    protected $dsn_prefix = 'mock';
    
    protected $quote_name_prefix = '"';
    
    protected $quote_name_suffix = '"';
    
    protected function quote($val)
    {
        return "'" . strtr(
            $val,
            [
                '\\' => '\\\\',
                "'" => "\'"
            ]
        ) . "'";
    }
}
